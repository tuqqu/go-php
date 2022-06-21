package main

func main() {
	test_1()
	test_2()
	test_3()
}

type (
	Celcius int
	Kelvin int
)

func test_1() {
	println("test_1")
	var c Celcius = 100
	var k Kelvin = celciusToKelvin(c)

	println("k =", k)
}

func celciusToKelvin(c Celcius) Kelvin {

	return Kelvin(c + 273)
}

func test_2() {
	println("test_2")

	type unsignedInt uint
	type anotherUnsignedInt unsignedInt

	var i unsignedInt = 1
	var j anotherUnsignedInt = anotherUnsignedInt(i + 2)
	var k uint = uint(j + 3)

	println(i, j, k)
}

func test_3() {
	println("test_3")

	type unsigned8Int uint8

	var x int = 1

	var y unsigned8Int = unsigned8Int(x) + 1

	println(y)
}
