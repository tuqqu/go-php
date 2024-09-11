package main

func main() {
	test_interfaceAny1()
	test_interfaceAny2()
	test_interfaceAny3()
	test_interfaceAny4()
}

// any value assignment
func test_interfaceAny1() {
	println("test_interfaceAny1")

	var a interface{}

	a = 1

	println(a == nil)
	println(a == 1)

	a = false

	println(a == nil)
	println(a == false)

	a = "string"

	println(a == nil)
	println(a == "string")

	a = []int{1, 2, 3}

	println(a == nil)

	a = map[string]int{"a": 1}

	println(a == nil)

	a = nil

	println(a == nil)
}

// value assigment after variable declaration with value
func test_interfaceAny2() {
	println("test_interfaceAny2")

	var a interface{} = nil

	a = "hello"

	println(a == nil)
	println(a == "hello")

	var b any = 4

	b = "hello"

	println(b == nil)
	println(b == "hello")
}

// var declaration with value
func test_interfaceAny3() {
	println("test_interfaceAny3")

	var a interface{} = []int{1, 2, 3}

	println(a == nil)

	var b any = [1]bool{true}

	println(b == nil)

	var c any = [...]struct{ Name string }{struct{ Name string }{Name: "John"}}

	println(c == nil)

	var d any = map[string]int{"a": 1}

	println(d == nil)

	var e any = nil

	println(e == nil)

	var f any = 1

	println(f == nil)
	println(f == 1)

	var g any = "hello"

	println(g == nil)
	println(g == "hello")

	var h any = false

	println(h == nil)
	println(h == false)

	var i any = 1.2

	println(i == nil)
	println(i == 1.2)

	var j any = 1.2 + 3.4i

	println(j == nil)
	println(j == 1.2+3.4i)

	var k any = 'a'

	println(k == nil)
	println(k == 'a')

	var l any = struct{ Name string }{Name: "John"}

	println(l == nil)
}

// struct type assignment
func test_interfaceAny4() {
	println("test_interfaceAny4")

	type Person struct {
		Name string
	}

	var a interface{} = Person{Name: "John"}

	println(a == nil)

	a = &Person{Name: "John"}

	println(a == nil)

	a = "hello"

	println(a == nil)

	var b bool = true

	a = b

	println(a == nil)
	println(a == true)
	println(a == b)

	var c any = 18.3

	a = c

	println(a == nil)
	println(a == 18.3)
	println(a == c)

	var d []int = []int{1, 2, 3}

	a = d

	println(a == nil)

	var e map[string]int

	a = e

	println(a == nil)
}
