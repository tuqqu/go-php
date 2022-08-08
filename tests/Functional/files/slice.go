package main

func main() {
	test_slice_1()
	test_slice_2()
}

func test_slice_1() {
	var a []byte = make([]byte, 3, 5)
	a[1] = 3
	println("len:", len(a))
	println("cap:", cap(a))
	println("slice:", a)
}

func test_slice_2() {
	var a []uint32 = []uint32{}
	println(a)

	var b []bool = make([]bool, 5)
	b[4] = true
	b = append(b, true)
	println(b)
	println(len(b))

	c := append([]string{"str1", "str2", "str3", "str4"}, "str5")
	println(c)

	twoD := make([][]int, 3)
	for i := 0; i < 3; i++ {
		innerLen := i + 1
		twoD[i] = make([]int, innerLen)
		for j := 0; j < innerLen; j++ {
			twoD[i][j] = i + j
		}
	}
	println(twoD)

	var slice = []int{0, 1, 2}
	var secondSlice = setFirst(66, slice)
	println(secondSlice)
	println(slice)
}

func setFirst(first int, slice []int) []int {
	slice[0] = first

	return slice
}
