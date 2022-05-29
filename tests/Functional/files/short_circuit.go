package main

func main() {
	test_AndTrue()
	test_AndFalse()
	test_OrTrue()
	test_OrFalse()
}

func test_AndTrue() {
	var s = "test_AndTrue"
	if returnTrue(s) && returnTrue(s) && returnTrue(s) {
		println(s + " end")
	}
}

func test_AndFalse() {
	var s = "test_AndFalse"
	if returnFalse(s) && returnFalse(s) && returnFalse(s) {
		println(s + " end")
	}
}

func test_OrTrue() {
	var s = "test_OrTrue"
	if returnTrue(s) || returnTrue(s) || returnTrue(s) {
		println(s + " end")
	}
}

func test_OrFalse() {
	var s = "test_OrFalse"
	if returnFalse(s) || returnFalse(s) || returnFalse(s) {
		println(s + " end")
	}
}

func returnTrue(s string) bool {
	println(s)
	return true
}

func returnFalse(s string) bool {
	println(s)
	return false
}
