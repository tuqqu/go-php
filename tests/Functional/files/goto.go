package main

func main() {
	test_goto_1()
	test_goto_2()
}

func test_goto_1() {
	println("test_goto_1")
	i := 0
start:
	for i < 10 {
		if i % 2 == 0 {
			i++
			goto start
		}
		println(i)
		i++
	}
}

func test_goto_2() {
	println("test_goto_2")

	var i = 0

	goto label1

label0:
	println("label0")

	if i > 0 {
		goto label2
	}

label1:
	println("label1")
	i++
	goto label0

label2:
	println("label2")
}
