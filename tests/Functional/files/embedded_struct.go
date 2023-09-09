package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
	test_5()
	test_6()
}

func test_1() {
	println("test_1")
	var a struct{ string } = struct{ string }{}
	a.string += "John"

	println(a.string)
}

func test_2() {
	println("test_2")
	var a struct {
		uint
		string
	} = struct {
		uint
		string
	}{uint: 100}

	// a.string is taken from stringifyied a.uint
	a.string = "Jane"

	println(a.uint)
	println(a.string)
}

func test_3() {
	println("test_3")
	var a struct {
		uint
		string
	} = struct {
		uint
		string
	}{uint: 100, string: "John"}

	println(a.uint)
	println(a.string)
}

func test_4() {
	println("test_4")
	type person struct {
		string
		int
	}

	var p1 person = person{string: "John"}
	var p2 person = person{string: "Jane", int: 18}
	var p3 *person = &p1
	p3.int = 21

	println(p1.string)
	println(p1.int)
	println(p2.string)
	println(p2.int)
	println(p3.string)
	println(p3.int)
}

func test_5() {
	println("test_5")

	type name = string
	type age int

	type person struct {
		name
		age
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

func test_6() {
	println("test_6")

	type name = string
	type age int

	type pet struct {
		nickname name
		age
		color string
	}

	type person struct {
		name
		age
		pet
	}

	var p1 person = person{name: "John"}
	p1.nickname = "Fido"
	p1.pet.nickname = "Spot"
	p1.pet.age = 2
	p1.pet.color = "white"
	p1.color = "brown"

	println(p1.name)
	println(p1.age)
	println(p1.nickname)
	println(p1.pet.nickname)
	println(p1.pet.age)
	println(p1.pet.color)
	println(p1.color)

	var p2 person = person{name: "Jane", age: 18, pet: pet{nickname: "Fluffy", age: 2, color: "white"}}
	p2.nickname = "Whiskers"
	p2.pet.nickname = "Furball"
	p2.pet.age = 3
	p2.pet.color = "black"
	p2.color = "white"

	println(p2.name)
	println(p2.age)
	println(p2.nickname)
	println(p2.pet.nickname)
	println(p2.pet.age)
	println(p2.pet.color)
	println(p2.color)

	p2.color = "black"
	p2.nickname = "Furball"

	println(p2.nickname)
	println(p2.color)
	println(p2.pet.nickname)
	println(p2.pet.color)
}
