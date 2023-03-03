package main

func main() {
	test_label_1()
	test_label_2()
	test_label_3()
	test_label_4()
}

func test_label_1() {
	println("test_label_1")
	var i int = 0

label:
	for ; i < 10; i += 2 {
		println("for loop", i)
		var j = 0

		for j < 5 {
			println("for loop inner", i, j)

			if j == 2 {
				println("break label", i, j)
				break label
			}

			j++
		}
	}
}

func test_label_2() {
	println("test_label_2")

	var i int

label:
	for i < 5 {
		println("for loop", i)
		i++

		if i == 4 {
			println("break label", i)
			break label
		}
	}
}

func test_label_3() {
	println("test_label_3")
	var i int = 0

label:
	for ; i < 10; i-- {
		println("for loop", i)

		j := 10
		i++

		for true {
			println("inner for loop", i, j)

			if j == 10 {
				j++
				println("inner if", i, j)
				break label
			}
		}
	}

	if i == 5 {
		println("end", i)
		return
	}

	println("goto", i)
	goto label
}

func test_label_4() {
	println("test_label_4")
	var i int
	var stop bool

label:
	for i < 10 {
		println("for loop", i)
		i++

		if stop {
			break
		}

	inner:
		for true {
			println("inner for loop", i)

			i++

			if stop {
				goto label
			}

			if i > 10 {
				println("inner if 2", i)
				break inner
			}

			break
		}

		stop = true
	}

	println("end", i)
}
