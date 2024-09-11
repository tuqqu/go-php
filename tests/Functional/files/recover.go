package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
	test_5()
	test_6()
}

func test_1() {
	println("test_1")

	var t = recover()
	println(t)
	println("Returned normally from test_1.")
}

func test_2() {
	println("test_2")

	var x, y = f()
	println("Returned normally from f.")
	println(x, y)
}

func f() (int, bool) {
	defer func() {
		if r := recover(); r != nil {
			println("Recovered in f")
			println(r == nil)
		}
	}()
	println("Calling g.")
	g(0)
	println("Returned normally from g.")

	return 9, true
}

func g(i int) bool {
	if i > 3 {
		println("Panicking!")
		panic(i)
	}

	defer println("Defer in g", i)
	println("Printing in g", i)
	g(i + 1)

	return true
}

func test_3() {
	println("test_3")

	defer customRecoverFromPanic("recovering from test_3")
	defer customRecoverFromPanic("second call to recover from test_3")

	customPanic("panicking from test_3")
}

func customPanic(msg string) {
	panic(msg)
}

func customRecoverFromPanic(msg string) int {
	var x, y = recover(), recover()
	println(x == nil)
	println(y)
	println(msg)

	return 9
}

func test_4() {
	println("test_4")

	a := []int{5, 6}
	val, err := checkAndGet(a, 2)

	println("Val: ", val)
	println("Error: ", err)

	val, err = checkAndGet(a, 1)

	println("Val: ", val)
	println("Error: ", err)
}

func checkAndGet(a []int, index int) (int, bool) {
	defer handleOutOfBounds()

	if index > (len(a) - 1) {
		panic("Out of bound access for slice")
	}

	return a[index], true
}

func handleOutOfBounds() {
	if r := recover(); r != nil {
		println("Recovering from panic:", r == nil)
	}
}

func test_5() {
	println("test_5")

	defer handleNilDereference()
	defer handleNilDereference()

	var f func() int
	var x = f()

	println("X: ", x)
}

func handleNilDereference() {
	if r := recover(); r != nil {
		println("Recovering from panic:", r == nil)
	} else {
		println("No panic")
	}
}

func test_6() {
	println("test_6")

	defer handleNilMapAssignment()

	var m map[string]int

	defer handleNilMapAssignment()
	defer handleNilMapAssignment()

	m["a"] = 1

	println("Returned normally from test_6.")
}

func handleNilMapAssignment() {
	if r := recover(); r != nil {
		println("Recovering from panic:", r == nil)
	} else {
		println("No panic")
	}
}
