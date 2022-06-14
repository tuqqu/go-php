package main

func main() {
	s := sVar

	test_1(s)
	test_2()
	test_3()
}

func test_1(s Str) {
	println("test_1")

	println(s + s)
}

func test_2() {
	println("test_2")

	println(aVar)
	println(aConst)
}

func test_3() {
	println("test_3")

	println(sVar)
	println(sConst)
}

var aVar int = aConst

const aConst int = 1 + 1

var sVar Str = sConst + sConst

const sConst Str = "foo"

type Str = string
