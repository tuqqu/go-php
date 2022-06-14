package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
}

type (
	Celcius = int
	Kelvin  = int
)

func test_1() {
	println("test_1")
	var c Celcius = 100
	var k Kelvin = celciusToKelvin(c)

	println("k =", k)
}

func celciusToKelvin(c Celcius) Kelvin {

	return c + 273
}

func test_2() {
	println("test_2")

	type Str = string
	type StrMap = map[Str]Str

	var t = make(StrMap)

	t["foo"] = "bar"

	println(t["foo"])
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
