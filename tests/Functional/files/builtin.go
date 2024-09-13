package main

func main() {
	test_copy()
	test_append()
	test_delete()
	test_new()
	test_len()
	test_cap()
	test_make()
	test_complex_real_imag()
	test_print_println()
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

func test_delete() {
	println("test_delete")

	// 1
	var s1 map[int]string = map[int]string{0: "A", 1: "B", 2: "C", 3: "D"}
	delete(s1, 2)
	println(len(s1), s1[0], s1[1], s1[2], s1[3])

	// 2
	var s2 map[string]int = map[string]int{"A": 0, "B": 1, "C": 2, "D": 3}
	delete(s2, "B")
	delete(s2, "C")
	println(len(s2), s2["A"], s2["B"], s2["C"], s2["D"])
}

func test_new() {
	println("test_new")

	// 1
	var p1 *int = new(int)
	*p1 = 5
	println(*p1)

	// 2
	var p2 *string = new(string)
	*p2 = "Hello"
	println(*p2)

	// 3
	var p3 *[]int = new([]int)
	*p3 = []int{0, 1, 2}
	println((*p3)[0], (*p3)[1], (*p3)[2])
}

func test_len() {
	println("test_len")

	// 1
	var s1 []int = []int{0, 1, 2, 3}
	println(len(s1))

	// 2
	var s2 map[int]string = map[int]string{0: "A", 1: "B", 2: "C", 3: "D"}
	println(len(s2))

	// 3
	var s3 string = "Hello, world!"
	println(len(s3))

	// 4
	var s4 [5]int = [5]int{0, 1, 2, 3, 4}
	println(len(s4))

	// 5
	var s5 *[5]int = new([5]int)
	println(len(s5))

	// 6
	var s6 *[5]int = nil
	println(len(s6))
}

func test_cap() {
	println("test_cap")

	// 1
	var s1 []int = make([]int, 3, 5)
	println(cap(s1))

	// 2
	var s2 []int = nil
	println(cap(s2))

	// 3
	var s3 *[5]int = new([5]int)
	println(cap(s3))

	// 4
	var s4 *[5]int = nil
	println(cap(s4))

	// 4
	var s5 [5]int = [5]int{0, 1, 2, 3, 4}
	println(cap(s5))
}

func test_complex_real_imag() {
	println("test_complex")

	// 1
	var c1 complex64 = 5 + 6i
	println(c1, real(c1), imag(c1))

	// 2
	var c2 complex128 = 7 + 8i
	println(real(c2), imag(c2))

	// 3
	var c3 complex64 = complex(9, 10)
	println(real(c3), imag(c3))

	// 4
	var c4 complex128 = complex(11, 12)
	println(real(c4), imag(c4))
}

func test_make() {
	println("test_make")

	// 1
	var s1 []int = make([]int, 3)
	println(len(s1), cap(s1))

	// 2
	var s2 []string = make([]string, 3, 5)
	println(len(s2), cap(s2))

	// 3
	var s3 map[int]string = make(map[int]string)
	println(len(s3))

	// 4
	var s4 map[string]int = make(map[string]int, 3)
	println(len(s4))
}

func test_print_println() {
	println("test_print")

	print("Hello, ")
	print("world!")
	print("hello", "world", 1, 2, 3)

	println("Hello,-")
	println("world!")
	println("hello", "world", 1, 2, 3)
}
