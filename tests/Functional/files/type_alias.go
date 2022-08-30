package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
	test_5()
	test_6()
}

type (
	Celsius = int
	Kelvin  = int
)

func test_1() {
	println("test_1")
	var c Celsius = 100
	var k Kelvin = celsiusToKelvin(c)

	println("k =", k)
}

func celsiusToKelvin(c Celsius) Kelvin {

	return c + 273
}

func test_2() {
	println("test_2")

	type Str = string
	type StrMap = map[Str]Str

	var t = make(StrMap)
	var s = StrMap{"a": "b"}

	t["foo"] = "bar"

	println(t["foo"])
	println(s["a"])
}

func test_3() {
	println("test_3")

	type Str = string

	var s1 Str = "foo"
	var s2 string = s1

	println(s2)
}

func test_4() {
	println("test_4")

	type unsignedInt = uint
	type anotherUnsignedInt = unsignedInt

	var i unsignedInt = 1
	var j anotherUnsignedInt = i + 2
	var k uint = j + 3

	println(i, j, k)
}

func test_5() {
	println("test_5")

	type IntSlice = []int
	var x IntSlice = IntSlice{1, 2, 3}

	x = append(x, 4)

	var y IntSlice = IntSlice(x)

	println(x[0], x[1], x[2], x[3], y[0], y[1], y[2], y[3])
}

func test_6() {
	println("test_6")

	type unsignedInt = uint

	var x int = 1

	var y unsignedInt = unsignedInt(x) + 1

	println(y)
}
