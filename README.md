# Go-PHP

Golang interpreter written in PHP.

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

This is a work-in-progress project.

Already implemented:

* see [tests](tests/Functional/files/)

To be implemented:

* channels and select statements
* type assertions
* interfaces
* panic recovering
* generics

## Development

install dependencies:

```
composer install
```

run tests:

```
make test
```

run `make help` for more commands.