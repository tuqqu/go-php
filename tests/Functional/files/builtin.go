package main

func main() {
	test_copy()
}

func test_copy() {
	println("test_copy")

	// 1
	var s1 = make([]int, 3)
	n1 := copy(s1, []int{0, 1, 2, 3})
	// n1 == 3, s1 == []int{0, 1, 2}
	println(n1, s1[0], s1[1], s1[2], len(s1))

	// 2
	s2 := []int{0, 1, 2}
	n2 := copy(s2, s2[1:])
	// n2 == 2, s2 == []int{1, 2, 2}
	println(n2, s2[0], s2[1], s2[2], len(s2))

	// 3
	var b = make([]byte, 5)
	n3 := copy(b, "Hello, world!")
	// n3 == 5, b == []byte("Hello")
	println(n3, b[0], b[1], b[2], b[3], b[4], len(b))
}
