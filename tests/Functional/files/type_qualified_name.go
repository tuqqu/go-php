package main

import "test_package_5_types"

func main() {
	test_1()
	test_2()
// 	test_3()
}

func test_1() {
	println("test_1")

	var i test_package_5_types.TestInt = 100

	println(i)

    type aliasInt = test_package_5_types.TestInt

	var j aliasInt = aliasInt(2) * i

	println(j)

	var b test_package_5_types.TestBoolMap = test_package_5_types.TestBoolMap{true: true, false: false}

	println(b[true])
    println(b[false])

    var point test_package_5_types.Point = test_package_5_types.Point{x: aliasInt(1), y: aliasInt(2)}

    println(point.x, point.y)
}

func test_2() {
    println("test_2")

    var i test_package_5_types.TestIntAliasOfCustom = 99

    println(i)

    var s test_package_5_types.TestStringAlias = "Hello"

    println(s)
}

// func test_3() {
//     println("test_3")
//
//     var x test_package_5_types.TestInt = 1
//
//     acceptCustomInt(x)
// }
//
// func acceptCustomInt(i test_package_5_types.TestInt) test_package_5_types.TestInt {
//     println("accepted:", i)
//
//     return i
// }
