package main

func main() {
	voidFunction()
	voidFunctionWithArg(12)

	var a = returnInt(1)
	println(a)

	var b, c = returnUintString(1, "string")
	println(b, c)
}

func voidFunction() {
}

func voidFunctionWithArg(x int) {
}

func returnInt(x int) int {
	return x + 2
}

func returnUintString(x uint8, s string) (uint8, string) {
	var retX = x * 2
	var retY = s + s

	return retX, retY
}
