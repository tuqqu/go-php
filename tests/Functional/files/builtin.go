package main

func main() {
	test_copy()
	test_append()
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

func test_append() {
	println("test_append")

	// 1
	var s1 []int = make([]int, 0)
	s1 = append(s1)
	println(len(s1), cap(s1))
	s1 = append(s1, 1)
	println(len(s1), cap(s1), s1[0])
	s1 = append(s1, 5, 6, 7)
	println(len(s1), cap(s1), s1[0], s1[1], s1[2], s1[3])

	// 2
	s2 := []string{"A", "B", "C", "D"}
	s2 = append(s2, "E", "F")
	println(len(s2), cap(s2), s2[0], s2[1], s2[2], s2[3], s2[4], s2[5])

	// 3 special case
	s3 := []byte{'a', 'b'}
	s3 = append(s3, 'c', 45, byte(uint(9)))
	println(len(s3), cap(s3), s3[0], s3[1], s3[2], s3[3])
	s3 = append(s3, "d"...)
	println(len(s3), cap(s3), s3[0], s3[1], s3[2], s3[3], s3[4])
	s3 = append(s3, "str"...)
	println(len(s3), cap(s3), s3[0], s3[1], s3[2], s3[3], s3[4], s3[5], s3[6], s3[7])
}
