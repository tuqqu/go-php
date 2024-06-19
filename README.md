# Go-PHP

Golang interpreter written in PHP.

## Example

```php
use GoPhp\Interpreter;

$interp = Interpreter::create(<<<GO
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

$result = $interp->run();
```

See [examples](examples/) for more.
To run examples:

```
cd ./examples/{name}
php main.php
```

## WIP

This is a toy project, not intended for production use.

To see what is already implemented, refer to [tests](tests/Functional/files/).

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

## Differences from the Go compiler

- No support for real goroutines, go statements run sequentially
