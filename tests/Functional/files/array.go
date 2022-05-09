package main

func main() {
	var a [5]uint32
	println(a)

	a[4] = 100
	println(a)
	println(a[4])
	println(len(a))

	b := [...]string{"str1", "str2", "str3", "str4"}
	println(b)

	var twoD [2][3]int
	for i := 0; i < 2; i++ {
		for j := 0; j < 3; j++ {
			twoD[i][j] = i + j
		}
	}
	println(twoD)

	var array = [...]int{0, 1, 2}
	var secondArray = setFirst(66, array)
	println(array)
	println(secondArray)
}

func setFirst(first int, array [3]int) [3]int {
	array[0] = first

	return array
}
