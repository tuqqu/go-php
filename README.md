# Go-PHP

Golang interpreter written in PHP.

TBD

# Example

TBD

```php
$interpreter = \GoPhp\Interpreter::fromString(<<<GO
    package main

    func main() {
        println("Hello, World!")
    }
GO);

$interpreter->run();
```

## WIP

This is a very much work-in-progress project.

Incomplete list of features:

Implemented:

see [tests](tests/Functional/files/)

TODO:

To be implemented:
* complex numbers
* channels and select statements
* structs and methods
* type assertions
* interfaces
* panic recovering
* imports
* lambda functions
* generics

Known bugs:
* goto from loop body
* underlying array capacity is not correctly set
* untyped string