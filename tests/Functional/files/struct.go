package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
	test_5()
}

func test_1() {
	println("test_1")
	var a struct{ name string } = struct{ name string }{}
	a.name += "John"

	println(a.name)
}

func test_2() {
	println("test_2")
	var a struct{ name string } = struct{ name string }{name: "John Doe"}

	println(a.name)
}

func test_3() {
	println("test_3")
	type person struct {
		name string
		age  int
	}

	var p1 person = person{name: "John"}
	var p2 person = person{name: "Jane", age: 18}
	var p3 *person = &p1
	p3.age = 21

	println(p1.name)
	println(p1.age)
	println(p2.name)
	println(p2.age)
	println(p3.name)
	println(p3.age)
}

func test_4() {
	println("test_4")
	var a *person = newPerson("John")
	a.age++

	println(a.name)
	println(a.age)
}

type person struct {
	name string
	age  int
}

func newPerson(name string) *person {
	p := person{name: name}
	p.age = 42

	return &p
}

func test_5() {
	println("test_4")
	type pair struct {
		personA *person
		personB *person
	}

	var a *person = newPerson("John")
	var b *person = newPerson("Jane")

	var p pair = pair{
		personA: a,
		personB: b,
	}

	println(p.personA.name)
	println(p.personB.name)

	p.personA.age++

	println(p.personA.age)
}
