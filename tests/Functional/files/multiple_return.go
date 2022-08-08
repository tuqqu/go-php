package main

func main() {
	test_1()
	test_2()
}

func test_1() {
	println("test_1")

	var a, b int = return_two()
	println(a, b)

	println(return_two())
}

func test_2() {
	println("test_2")

	a, b, c := return_three()
	println(a, b, c)

	println(return_three())

	accept_three(return_three())
}

func return_two() (int, int) {
	return 1, 2
}

func return_three() (string, int, bool) {
	return "string", 2, true
}

func accept_three(a string, b int, c bool) {
	println(a, b, c)
}
