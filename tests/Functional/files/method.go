package main

func main() {
	test_method_1()
	test_method_2()
	test_method_3()
}

type int uint
type empty struct{}
type person struct {
	name string
}

func test_method_1() {
	println("test_method_1")

	var i int = 10
	var res2 = i.methodInt1()
	println(res2)
}

func test_method_2() {
	println("test_method_2")

	var e empty = empty{}
	var res1 = e.methodEmpty2()
	println(res1)

	var res2 = e.methodEmpty1()
	println(res2)
}

func test_method_3() {
	println("test_method_3")

	var p person = person{
		name: "test",
	}

	var pp = &p

	var res1 = p.methodPerson1()
	println(p.name)
	println(res1)

	var res2 = p.methodPerson2()
	println(p.name)
	println(p == *pp)
	println(res2)
}

func (i int) methodInt1() int {
	println("called methodInt1")

	return i * 2
}

func (e empty) methodEmpty1() int {
	println("called methodEmpty1")

	return 1
}

func (e *empty) methodEmpty2() int {
	println("called methodEmpty2")

	return 2
}

func (p person) methodPerson1() int {
	println("called methodPerson1")

	p.name = "test2"

	return 1
}

func (p *person) methodPerson2() int {
	println("called methodPerson2")

	p.name = "test3"

	return 2
}
