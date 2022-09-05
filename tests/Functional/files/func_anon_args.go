package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
}

func test_1() {
	println("test_1")

	anonArg(12)
}

func test_2() {
	println("test_2")

	manyAnonArgs(true, "string", anonArg, [...]int{1})
}

func test_3() {
	println("test_3")

	var res1, res2 = anonArgWithReturns([]uint{1, 2, 3})
	println(res1, res2)
}

func test_4() {
	println("test_4")

	anonVariadicArgs([1]int{1}, [1]string{"h"}, [1]string{"i"})
	anonVariadicArgs([1]int{1}, [1]string{"hi"})
}

func anonArg(int) {
	println("anonArg called")
}

func manyAnonArgs(bool, string, func(int), [1]int) {
	println("manyAnonArgs called")
}

func anonArgWithReturns([]uint) (int, string) {
	println("anonArgWithReturns called")
	return 1, "string"
}

func anonVariadicArgs([1]int, ...[1]string) {
	println("anonVariadicArgs called")
}
