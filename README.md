# Laravel Myanmar NRC

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)
[![Tests](https://github.com/laranex/laravel-myanmar-nrc/actions/workflows/tests.yml/badge.svg)](https://github.com/laranex/laravel-myanmar-nrc/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)
[![License](https://img.shields.io/packagist/l/laranex/laravel-myanmar-nrc.svg?style=flat-square)](LICENSE.md)

Myanmar NRC for Laravel: validate, parse and format NRC numbers in English and Myanmar. Built for humans and AI agents.

## Why Laravel Myanmar NRC

A Myanmar NRC number is more than a pattern: its state, township and type must exist, and the township must belong to the state. Laravel Myanmar NRC checks all of that against the real NRC data and gives the number back in English or Myanmar:

- **The NRC data is included.** All 15 state codes, 471 townships and 6 NRC types, in English and Myanmar, seeded into your database with one command or read straight from the bundled JSON file.
- **One validation rule.** `new MyanmarNRC` rejects malformed numbers, unknown ids and townships from another state, with its error message in English and Myanmar.
- **Readable in both languages.** `parse()` turns `12-284-1-123456` into `12/DAGAYA(N)123456` or `၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆`, Myanmar digits included.
- **One canonical form to store.** `normalize()` turns `012-0284-01-123456` into `12-284-1-123456`, so equal NRCs are equal strings and unique checks don't miss duplicates.
- **Database or JSON file.** Use the `nrc_states`, `nrc_townships` and `nrc_types` tables with their Eloquent models, or skip the database and read the JSON file, per call if you like. Your own data file works too.
- **Cached lookups.** The database backend reads the tables once and caches them, so other requests and workers skip the queries.

## Installation

```bash
composer require laranex/laravel-myanmar-nrc
php artisan migrate
php artisan mm-nrc:seed
```

Requirements: PHP ^8.1 with ext-ctype and ext-json, and Laravel 10 to 13.

## Documentation

The full documentation lives at **[laranex.vercel.app/laravel-myanmar-nrc](https://laranex.vercel.app/laravel-myanmar-nrc)**:

- [Introduction](https://laranex.vercel.app/laravel-myanmar-nrc/introduction): why Laravel Myanmar NRC, requirements and a quick example
- [Installation](https://laranex.vercel.app/laravel-myanmar-nrc/installation): installing, running the migrations and seeding the NRC data
- [AI Agent Skill](https://laranex.vercel.app/laravel-myanmar-nrc/ai-agent-skill): the agent skill for Claude Code, Codex, Cursor and other coding agents
- [Configuration](https://laranex.vercel.app/laravel-myanmar-nrc/configuration): the locale, the data file, the database or JSON backend and the cache
- [Upgrading](https://laranex.vercel.app/laravel-myanmar-nrc/upgrading): upgrading from v2 to v4
- [Usage](https://laranex.vercel.app/laravel-myanmar-nrc/usage): validating, normalizing and parsing NRC numbers in English and Myanmar

## License

MIT. See [LICENSE.md](LICENSE.md).
