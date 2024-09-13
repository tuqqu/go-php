package main

func main() {
	test_method_1()
	test_method_2()
	test_method_3()
	test_method_4()
}

type int uint
type myBool bool

type empty struct{}
type person struct {
	name string
}

func test_method_1() {
	println("test_method_1")

	var i int = 10
	var res1 = i.methodInt1()
	println(res1)

	var i2 *int = &i
	var res2 = i2.methodInt1()
	println(res2)
	println(*i2)
	println(i)

	var s myBool
	s = true
	var res3 = s.methodMyBool1()
	println(res3)
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

	var p2 *person = &p

	var res1 = p.methodPerson1("test2")
	println(p.name)
	println(p == *p2)
	println(res1)

	var res2 = p.methodPerson2("test2")
	println(p.name)
	println(p == *p2)
	println(res2)
}

func test_method_4() {
	println("test_method_4")

	var p person = person{
		name: "test",
	}

	var p2 *person = &p

	var p3 **person = &p2

	var res1 = p.methodPerson1("test2")
	println(p.name)
	println(p == *p2)
	println(p == **p3)
	println(res1)

	var res2 = p.methodPerson2("test3")
	println(p.name)
	println(p == *p2)
	println(p == **p3)
	println(res2)

	var res3 = p2.methodPerson1("test4")
	println(p2.name)
	println(res3)

	var res4 = p2.methodPerson2("test5")
	println(p2.name)
	println(res4)

	var res5 = (*p3).methodPerson1("test6")
	println(p.name)
	println(p2.name)
	println((*p3).name)
	println(res5)

	var res6 = (*p3).methodPerson2("test7")
	println(p.name)
	println(p2.name)
	println((*p3).name)
	println(res6)
}

func (i int) methodInt1() int {
	println("called methodInt1")

	return i * 2
}

func (s myBool) methodMyBool1() myBool {
	println("called methodMyBool1")

	return !s
}

func (e empty) methodEmpty1() int {
	println("called methodEmpty1")

	return 1
}

func (e *empty) methodEmpty2() int {
	println("called methodEmpty2")

	return 2
}

func (p person) methodPerson1(name string) int {
	println("called methodPerson1")

	p.name = name

	return 1
}

func (p *person) methodPerson2(name string) int {
	println("called methodPerson2")

	p.name = name

	return 2
}
