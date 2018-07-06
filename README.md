# PHP Router Benchmark

[![Build Status](https://travis-ci.org/jails/php-router-benchmark.svg?branch=master)](https://travis-ci.org/jails/php-router-benchmark)

The intent here is to benchmark and also inventory all popular PHP routing solutions around.

### Jasny Proof of concept

Accepting the constraint that a segment is either static or variable (and never both), we can do without any type of
pattern matching. Instead we can use `explode('/', $url)` to get all segments and use `switch` statements.

_Note that Jasny will perform worst the first time you run the benchmark, since we can't take advantage of the opcode
cache. Run the tests a couple of times to see how the poc actually performs._

## Installation

Clone the repo then:

```bash
composer install
./benchmark.php
```

## Benchmarking process

The current test creates 100 unique routes with 3 variables placeholder each.

Example of route: `/controller1/action1/{id}/{arg1}/{arg2}`

This benchmarking will be runned on the following three different situations for both path & subdomain:
* the best case (i.e when a request matches the first route for all differents HTTP method)
* the worst case (i.e when a request matches the last route for all differents HTTP method)
* the average case (i.e the mean which is probably the most realistic test).

And all tests will be runned using the following sets of routes:
* in the first set all routes matches all HTTP methods.
* in the second set all routes matches only a single HTTP method.

The benchmarked routing implementations are:

* Jasny = A proof of concept of generating static code
* [Router](https://github.com/crysalead/router)
* [Li3](https://github.com/UnionOfRAD/lithium)
* [FastRoute](https://github.com/nikic/FastRoute)
* [FastRoute*](https://github.com/jails/FastRoute) = FastRoute + [a classic routing strategy](https://github.com/jails/FastRoute/commit/114676515b636b637f6cac53945c2e04875b60eb)
* [Symfony](https://github.com/symfony/routing)
* [Aura3](https://github.com/auraphp/Aura.Router)
* [PHRoute](https://github.com/mrjgreen/phroute)

## Results

```
################### With Routes Supporting All HTTP Methods ###################



=============================== Best Case (path) ===============================

Aura3           100% | ████████████████████████████████████████████████████████████  |
Symfony          61% | ████████████████████████████████████                          |
Jasny            47% | ████████████████████████████                                  |
Router           43% | █████████████████████████                                     |
Laravel          29% | █████████████████                                             |
FastRoute*       13% | ███████                                                       |
FastRoute        12% | ██████                                                        |
PHRoute           8% | ████                                                          |
Li3               7% | ████                                                          |


============================= Average Case (path) =============================

Jasny           100% | ████████████████████████████████████████████████████████████  |
Symfony          46% | ███████████████████████████                                   |
Router           43% | █████████████████████████                                     |
Aura3            22% | █████████████                                                 |
Laravel          21% | ████████████                                                  |
FastRoute*       17% | ██████████                                                    |
FastRoute        16% | █████████                                                     |
PHRoute          11% | ██████                                                        |
Li3               9% | █████                                                         |


============================== Worst Case (path) ==============================

Jasny           100% | ████████████████████████████████████████████████████████████  |
Router           41% | ████████████████████████                                      |
Symfony          36% | █████████████████████                                         |
FastRoute*       21% | ████████████                                                  |
FastRoute        20% | ███████████                                                   |
Laravel          15% | █████████                                                     |
Aura3            15% | ████████                                                      |
PHRoute          14% | ████████                                                      |
Li3               9% | █████                                                         |


============================ Best Case (sub-domain) ============================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        53% | ███████████████████████████████                               |
Symfony       24% | ██████████████                                                |
Aura3          6% | ███                                                           |
Laravel        5% | ███                                                           |
Li3            5% | ███                                                           |


========================== Average Case (sub-domain) ==========================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        41% | ████████████████████████                                      |
Symfony       29% | █████████████████                                             |
Aura3          6% | ███                                                           |
Li3            6% | ███                                                           |
Laravel        6% | ███                                                           |


=========================== Worst Case (sub-domain) ===========================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        24% | ██████████████                                                |
Symfony       22% | ████████████                                                  |
Li3            5% | ██                                                            |
Laravel        5% | ██                                                            |
Aura3          4% | ██                                                            |


############## With Routes Supporting Only A Single HTTP Methods ##############



=============================== Best Case (path) ===============================

Aura3           100% | ████████████████████████████████████████████████████████████  |
Symfony          74% | ████████████████████████████████████████████                  |
Jasny            71% | ██████████████████████████████████████████                    |
FastRoute*       63% | █████████████████████████████████████                         |
Router           57% | ██████████████████████████████████                            |
PHRoute          40% | ███████████████████████                                       |
Laravel          40% | ███████████████████████                                       |
Li3              33% | ███████████████████                                           |
FastRoute        31% | ██████████████████                                            |


============================= Average Case (path) =============================

Jasny           100% | ████████████████████████████████████████████████████████████  |
FastRoute*       84% | ██████████████████████████████████████████████████            |
FastRoute        75% | █████████████████████████████████████████████                 |
PHRoute          53% | ███████████████████████████████                               |
Router           51% | ██████████████████████████████                                |
Symfony          44% | ██████████████████████████                                    |
Li3              27% | ████████████████                                              |
Laravel          26% | ███████████████                                               |
Aura3            20% | ████████████                                                  |


============================== Worst Case (path) ==============================

Jasny           100% | ████████████████████████████████████████████████████████████  |
FastRoute*       76% | █████████████████████████████████████████████                 |
FastRoute        74% | ████████████████████████████████████████████                  |
PHRoute          46% | ███████████████████████████                                   |
Router           39% | ███████████████████████                                       |
Symfony          27% | ████████████████                                              |
Li3              20% | ███████████                                                   |
Laravel          15% | █████████                                                     |
Aura3            12% | ██████                                                        |


============================ Best Case (sub-domain) ============================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        58% | ██████████████████████████████████                            |
Symfony       29% | █████████████████                                             |
Li3           10% | █████                                                         |
Laravel       10% | █████                                                         |
Aura3          7% | ████                                                          |


========================== Average Case (sub-domain) ==========================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        31% | ██████████████████                                            |
Symfony       19% | ███████████                                                   |
Laravel        7% | ████                                                          |
Li3            6% | ███                                                           |
Aura3          4% | ██                                                            |


=========================== Worst Case (sub-domain) ===========================

Jasny        100% | ████████████████████████████████████████████████████████████  |
Router        31% | ██████████████████                                            |
Symfony       19% | ███████████                                                   |
Laravel        7% | ████                                                          |
Li3            7% | ███                                                           |
Aura3          4% | ██                                                            |

```
