.PHONY = build run

build:
	docker build -t alcohol/pastebin:latest .

run:
	docker run --rm -it -p 8000:8000 -v $(value CURDIR):/app alcohol/pastebin

server:
	php -S 0.0.0.0:8000 -t web
