# Comproso Framework Package

Comproso is a PHP framework for web and computer based testing in behavioral sciences, especially psychology and other behavioral sciences.

## Warning
### Disclaimer
Comproso is an Open Source software under AGPL-3.0 license. Nonetheless, behavioral assessment requires the consideration of ethical and legal issues for test developers (authors), test publishers (providers), and test users. See the [ITC Guidlines](https://www.intestcom.org/page/5) for an example.

**Use this framework only in consideration of ethical and legal issues!**

### Developemental status
This software is still under development. Even if it could run stable, a proper configuration requires some knowledge using [composer](https://getcomposer.org/) and [laravel](https://laravel.com/). Someday, a ready to run installation bundle will be provided. If you are having trouble during setup/installation/configuration/using the framework, feel free to ask for [support](https://github.com/comproso/framework/issues).

## Installation
Coming soon…

## Configuration
Coming soon…

### Notice
Be aware that Comproso uses *sessions*.
* You shouldn't use `cookie` as session driver option to avoid compromising possibilities.
* The `database` session driver may have problems with CSRF protection.
* We recommend to use `redis` or `memcached` for web based and `file` for laboratory use.

## Information
Further information are available on [comproso.org](https://comproso.org/).
Developers will be find some information in our documentation provided at [comproso.github.io/framework/](http://comproso.github.io/framework/).

## License
Copyright (C) 2015-2017 Thiemo Kunze <kunze (ät) wangaz (dot) com>

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

## Information for test authors and developers
Even if AGPL is forcing code forks also to publish under AGPL license, a test (or a project) is not: As tests are "only" linked to the framework, proprietary tests are imaginable. We strongly recommend a copyleft license, see [choosealicense.com](https://choosealicense.com) and [comproso.org/os](https://comproso.org/os) for further information. **Be aware of ethical and legal issues due to test publishing!**
