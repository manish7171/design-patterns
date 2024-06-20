#!/usr/bin/env bash
docker run --rm --interactive --tty \
	--volume ./:/app \
	--user $(id -u):$(id -g) \
	php:8.1-cli-alpine /bin/sh
