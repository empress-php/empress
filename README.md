[![Build Status](https://travis-ci.com/empress-php/empress.svg?branch=master)](https://travis-ci.com/empress-php/empress)
[![Coverage Status](https://coveralls.io/repos/github/empress-php/empress/badge.svg)](https://coveralls.io/github/empress-php/empress)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Work in progress ⚡

# Empress
Empress is a flexible microframework for creating async web applications. It's based on the Amp concurrency framework.
Its name is a portmanteau of Express and Amp as Empress's simplicity was first inspired by Express.js. Later, many useful ideas were incorporated from [Spark](http://sparkjava.com/) and [Javalin](https://javalin.io/) Ultimately it's also the name of one of the cards from Major Arcana, a part of the tarot deck.

The main focus is on simplicity. Notable features include:
- [x] Before & after filters
- [x] Easy access to request and response objects
- [x] Support for injectable controllers via PSR-11
- [x] Easy response handling with methods like respond(), json() and html()
- [x] Access to request params using array notation: `$ctx['param']`
- [x] Builtin support for session middleware

Roadmap
- [ ] Filter chain
- [ ] Status and exception mapping
- [ ] Support for templates
