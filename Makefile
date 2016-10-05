.PHONY = build run

build:
	docker build -t alcohol/pastebin:latest .

run:
	docker run --rm -it -p 8000:8000 -v $(value CURDIR):/app alcohol/pastebin
