package main

func main() {
	test_1()
	test_2()
}

func test_1() {
	println("test_1")
	const a = 10

	var b = a
	var c int = a
	var d int8 = a
	var e uint = a

	println(a, b, c, d, e)
}

func test_2() {
	println("test_2")
	const a = true

	var b = a
	var c bool = a
	const d bool = a

	println(a, b, c)
}
