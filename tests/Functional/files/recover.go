package main

func main() {
	testRecover_1()
	testRecover_2()
}

func testRecover_1() {
	println("testRecover_1")

	var t = recover()
	println(t)
	println("Returned normally from testRecover_1.")
}

func testRecover_2() {
	println("testRecover_2")

	var x, y = f()
	println("Returned normally from f.")
	println(x, y)
}

func f() (int, bool) {
	defer func() {
		if r := recover(); r != nil {
			println("Recovered in f")
			println(r)
		}
	}()
	println("Calling g.")
	g(0)
	println("Returned normally from g.")

	return 9, true
}

func g(i int) {
	if i > 3 {
		println("Panicking!")
		panic(i)
	}

	defer println("Defer in g", i)
	println("Printing in g", i)
	g(i + 1)
}
