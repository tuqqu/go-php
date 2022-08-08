package main

func main() {
	test_1()
	test_2()
	test_3()
}

func test_1() {
	println("test_1")

	var a, b int

	a, b = split(17, 0)
	println(a, b)

	a, b = split(17, 1)
	println(a, b)

	a, b = split(17, 2)
	println(a, b)

	a, b = split(17, 3)
	println(a, b)

	a, b = split(17, 4)
	println(a, b)
}

func test_2() {
	println("test_2")

	println(*double(1))
	println(*double(2))
	println(*double(6))
}

func test_3() {
	println("test_3")

	a, b, c, d := noop()

	println(a, b, c, d)
}

func split(sum int, mode int) (x, y int) {
	println("original values:", x, y)
	x = sum * mode
	x = sum * 4 / 9
	y = sum - x

	if mode == 0 {
		return y, x
	}

	if mode == 1 {
		return
	}

	if mode == 2 {
		return 3, 1
	}

	if mode == 3 {
		r := x
		t := y

		return r, t
	}

	return
}

func double(x int) (result *uint64) {
	var y uint64 = uint64(x) * 2
	result = &y

	return
}

func noop() (str string, num uint, array []int, b bool) {
	return
}
