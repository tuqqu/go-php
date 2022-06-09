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

* basic types (ints, floats, strings, bools)
* function types
* var, const, function declarations
* control flows, loops (for, for range, while, if, switch)
* goto and labels
* builtin.go (mostly)
* maps
* arrays
* slices, slicing
* nil values
* references, pointers and pointer types
* multiple return values
* iota
* variadic functions
* defer statements
* type conversions

see [tests](tests/Functional/files/)

TODO:

To be implemented:
* complex numbers
* go statements
* channels and select statements
* structs and methods
* type declarations
* type assertions
* interfaces
* panic recovering
* imports
* lambda functions
* generics

Known bugs:
* goto from loop body
* underlying array capacity is not correctly set
