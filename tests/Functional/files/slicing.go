package main

func main() {
	test_array_1()
	test_array_2()
	test_array_3()
	test_string()
}

func test_array_1() {
	println("test_array_1")

	a := [5]int{1, 2, 3, 4, 5}

	s := a[1:4]
	println(cap(s))

	s[0] = 0
	println(a)
	println(s)

	s2 := append(s, 6)
	println(cap(s2))
	s2[3] = 7

	println(a)
	println(s)
	println(s2)
}

func test_array_2() {
	println("test_array_2")

	a := [...]int{1, 2, 3, 4, 5, 6}
	s := a[1:5:5] // no free space
	s2 := append(s, 99)
	s[1] = 666
	s2[0] = 112
	println(a)
	println(s)
	println(s2)
}

func test_array_3() {
	println("test_array_3")

	a := [...]int{1, 2, 3, 4, 5, 6}
	s := a[1:5:6] // 1 free space
	s2 := append(s, 99)
	s[1] = 666
	s2[0] = 112
	println(a)
	println(s)
	println(s2)
}

func test_string() {
	println("test_string")

	var s string = "some_string"
	println(s[:])
	println(s[2:5])
	println(s[:5])
	println(s[4:])

	println("string"[:3])
}
