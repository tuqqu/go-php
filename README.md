# Go-PHP

Golang interpreter written in PHP.

TBD

# Example

```php
use GoPhp\Interpreter;

$interpreter = new Interpreter(<<<GO
    package main
    
    type person struct {
        name string
        age  int
    }
    
    func newPerson(name string) *person {
        p := person{name: name}
        p.age = 42

        return &p
    }

    func main() {
        s := newPerson("John Doe")
    
        println("Hello, " + s.name)
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
* methods
* type assertions
* interfaces
* panic recovering
* lambda functions
* generics

Known bugs:
* goto from loop body
* underlying array capacity is not correctly set
* untyped string
* weird cases for type definitions