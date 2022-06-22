package main

func main() {
	test_1()
}

func test_1() {
    println("test_1")
    go call()
}

func call() int {
    return 1
}
