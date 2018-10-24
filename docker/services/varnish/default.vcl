
/* When not explicitly using return() in a vcl routine, it will continue executing the builtin vcl routine, see:
 *  https://github.com/varnishcache/varnish-cache/blob/master/bin/varnishd/builtin.vcl
 */

vcl 4.0;

backend default {
  .host = "nginx";
  .port = "8000";
  .connect_timeout = 1s;
  .first_byte_timeout = 10s;
  .between_bytes_timeout = 4s;
}

acl invalidators {
  "localhost";
  "127.0.0.1";
  "10.0.0.0"/8;
  "172.16.0.0"/12;
  "192.168.0.0"/16;
}

import std;

/* Include fos/http-cache helper subroutines */
include "fos_ban.vcl";
include "fos_purge.vcl";

sub vcl_recv {
  if (req.method == "PRI") {
    /* This will never happen in properly formed traffic (see: RFC7540) */
    return (synth(405));
  }

  if (!req.http.host &&
    req.esi_level == 0 &&
    req.proto ~ "^(?i)HTTP/1.1") {
      /* In HTTP/1.1, Host is required. */
      return (synth(400));
  }

  /* Handle BAN requests */
  call fos_ban_recv;

  /* Handle PURGE requests */
  call fos_purge_recv;

  if (req.method != "GET" &&
    req.method != "HEAD" &&
    req.method != "PUT" &&
    req.method != "POST" &&
    req.method != "TRACE" &&
    req.method != "OPTIONS" &&
    req.method != "DELETE" &&
    req.method != "PATCH") {
      /* Non-RFC2616 or CONNECT which is weird. */
      return (synth(404, "Non-valid HTTP method!"));
  }

  if (req.http.Upgrade ~ "(?i)websocket") {
    /* Websocket support - https://www.varnish-cache.org/docs/4.0/users-guide/vcl-example-websockets.html */
    return (pipe);
  }

  if (req.method != "GET" && req.method != "HEAD") {
    /* We only deal with GET and HEAD by default */
    return (pass);
  }

  if (req.http.Authorization) {
    /* Not cacheable by default */
    return (pass);
  }

  /* Signal to backend we are capable of handling ESIs */
  set req.http.Surrogate-Capability = "retail=ESI/1.0";

  /* Cleanup request */
  call cleanup_request;

  return (hash);
}

sub vcl_backend_response {
  call fos_ban_backend_response;

  if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
    unset beresp.http.Surrogate-Control;
    set beresp.do_esi = true;
  }

  /* Don't cache 50x responses */
  if (beresp.status >= 500) {
    /* This check is important. If is_bgfetch is true, it means that we've found and returned the cached object to the client,
       and triggered an asynchoronus background update. In that case, if it was a 5xx, we have to abandon, otherwise the previously cached object
       would be erased from the cache (even if we set uncacheable to true). Varnish 5.2+ required. */
    if (bereq.is_bgfetch) {
      return (abandon);
    }

    /* Even if we couldn't send a previous successful response from the cache, we should never cache a 5xx response. */
    set beresp.uncacheable = true;
  }

  /* Allow stale content */
  set beresp.grace = 1h;
}

sub vcl_pass {
  set req.http.X-Varnish-TTL = 0;
  set req.http.X-Varnish-Cache = "pass";
}

sub vcl_hit {
  set req.http.X-Varnish-TTL = obj.ttl;
  set req.http.X-Varnish-Cache = "hit";
}

sub vcl_miss {
  set req.http.X-Varnish-TTL = 0;
  set req.http.X-Varnish-Cache = "miss";
}

sub vcl_deliver {
  call fos_ban_deliver;

  set resp.http.X-Varnish-TTL = req.http.X-Varnish-TTL;
  set resp.http.X-Varnish-Cache = req.http.X-Varnish-Cache;

  set resp.http.X-Container-Varnish = server.hostname;
}

sub cleanup_request {
  /* Normalize the header, remove the port */
  set req.http.Host = regsub(req.http.Host, ":[0-9]+", "");

  /* Normalize the query arguments */
  set req.url = std.querysort(req.url);

  if (req.url ~ "\#") {
    /* Strip hash, server doesn't need it */
    set req.url = regsub(req.url, "\#.*$", "");
  }

  if (req.url ~ "\?$") {
    /* Strip a trailing ? if it exists */
    set req.url = regsub(req.url, "\?$", "");
  }

  /* Strip Cookies */
  if (req.http.Cookie) {
    unset req.http.Cookie;
  }
}
