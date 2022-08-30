package main

func main() {
	test_int()
}

func test_int() {
	println("test_int")

	println(+9)
	println(-9)
	println(^9)

	println(1 + 2)
	println(4 - 100)
	println(5 * (-9))
	println(100 / (-9))
	println(43 % 4)

	println(43 ^ 4)
	println(6 &^ 3)
	println(32 & 2)
	println(14 | 2)
	println(1 >> 4)
	println(6 << 9)

	var x, y, c int = 4, 5, 6

	x += y
	println(x)
	x -= c
	println(x)
	c /= x
	println(c)
	y %= x
	println(y)
	x *= 45
	println(x)
	x ^= 3
	println(x)
	x &= 3
	println(x)
	x |= 3
	println(x)
	x &^= 6
	println(x)
	x >>= 1
	println(x)
	x <<= 3
	println(x)
}
