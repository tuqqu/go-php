package main

func main() {
	test_1()
}

const _ int = 1
const _, a = "string", false
const (
	_ = iota
	_
	_ = 1
	b = 1
)

var (
	_       = false
	_, _, c = 1, 2, 3
)

var _ uint = 100

func _() {}

func _(_ int) (_ int, _ string) {
	var _ = 100
	type _ = int
	return
}

type _ = string
type (
	_ uint
	_ = string
)

func test_1() {
	println("test_1")

	var _, _ = 1, 2

	_, d := 0, 0
	println(d)

	_ = 100

	for _, _ = range []int{1, 2, 3} {
		println("iteration")
	}

	for _, e := range []int{1, 2, 3} {
		println("iteration", e)
	}

	for f, _ := range []int{1, 2, 3} {
		println("iteration", f)
	}

	if _ = call(); true {
		_ = 1
	}
}

func call() int {
	println("call")
	return 1
}
