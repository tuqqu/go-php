package main

func main() {
	test_assignment()
	test_iiffe()
	test_closure()
}

func test_assignment() {
	println("test_assignment")

	// 1
	var f1 = func(int) { println("hi") }
	f2 := func(x int) { println("bye", x) }

	f1 = f2
	f1(1)
	f2(2)

	// 2
	var f3 func(uint, ...struct{ x int }) struct{ y int }

	f3 = f99

	println(f3(4, struct{ x int }{x: 1}).y)

	// 3
	var f5 func() (int, string) = func() (x int, y string) {
		x, y = 55, "some_string"
		println("inside closure with named return params")
		return
	}

	var i, s = f5()

	println(i, s)

	f5 = func() (int, string) { return 1, "str" }

	i, s = f5()

	println(i, s)
}

func f99(uint, ...struct{ x int }) struct{ y int } {
	return struct{ y int }{y: 6}
}

func test_iiffe() {
	println("test_iiffe")

	func(int) {
		println("inside IIFE 1")
	}(1)

	func(x ...string) {
		println("inside IIFE 2")

		for y := range x {
			println(y)
		}
	}("first", "second", "third")

	func(change func(int) int) {
		println("inside IIFE 3")
		var x = 3
		x = change(x)
		println(x)
	}(func(x int) int { return x * x })
}

func test_closure() {
	println("test_closure")

	var i int = 1

	var inc = func() int {
		i++

		return i + 10
	}

	x := inc()
	println(x)
	println(i)

	x = inc()
	println(x)
	println(i)
}
