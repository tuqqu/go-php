package main

var i int = 0

func main() {
	test_defer_1()
	test_defer_2()
}

func test_defer_1() {
	defer print_deferred_msg("first", i)
	i++
	var j = test_defer_inner(5)
	defer print_deferred_msg("second", j)

	println("test_defer_1")
}

func test_defer_inner(j int) int {
	defer print_deferred_msg("inner", j+i)

	println("test_defer_inner")

	return j + 1
}

func test_defer_2() {
	for j := 0; j < 5; j++ {
		defer print_deferred_msg("loop", j*2)
	}

	println("test_defer_2")
}

func print_deferred_msg(msg string, i int) {
	println("deferred "+msg, i)
}
