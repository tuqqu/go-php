package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
}

func test_1() {
    println("test_1")

	m := make(map[string]int)

	m["k1"] = 7
	m["k2"] = 13
	println("map:", m["k1"], m["k2"])

	v1 := m["k1"]
	println("v1: ", v1)

	println("len:", len(m))

	delete(m, "k2")
	println("map:", m["k1"], m["k2"])

	value, ok := m["k2"]
	println("value:", value)
	println("ok:", ok)

	unknown, notSet := m["k99"]
	println("unknown: ", unknown)
	println("notSet: ", notSet)

	unknown, notSet = m["k99"]
	println("unknown: ", unknown)
	println("notSet: ", notSet)

	for k, v := range m {
		println("k,v: ", k, " ", v)
	}
}

func test_2() {
    println("test_2")

	n := map[string]uint{"foo": 1, "bar": 2}
	println("map:", n["foo"], n["bar"])

	setFooHundred(n)

	println("map:", n["foo"], n["bar"])

	n["bar"] = 0
	var v1, v1ok = n["bar"]
	var v2, v2ok = n["baz"]

	println(v1, v1ok)
	println(v2, v2ok)
}

func setFooHundred(m map[string]uint) {
	m["foo"] = 100
}

func test_3() {
    // map with array key
    println("test_3")

    type TestMap = map[[2]int]string
    type TestArray = [2]int

    var m TestMap = make(TestMap)
    m[[2]int{1,2}] = "string1"

    println(m[[2]int{1,2}])

    var a TestArray = [2]int{5,6}

    m = TestMap{
        [2]int{1,2}: "string2",
        TestArray{3,4}: "string3",
        a: "string4",
    }

    println(m[TestArray{1,2}])
    println(m[[2]int{3,4}])
    println(m[a])
    println("len:", len(m))

    a[0] = 1
    a[1] = 2
    println(m[a])

    unknown, notSet := m[[2]int{99,99}]
    println("unknown: ", len(unknown))
    println("notSet: ", notSet)

    for k, v := range m {
        println("k: ", k[0], " ", k[1])
        println("v: ", v)
    }
}

func test_4() {
    // map with struct key
    println("test_4")

    type TestMap = map[struct{a, b int}]string
    type TestStruct = struct{a, b int}

    var m TestMap = make(TestMap)
    m[struct{a, b int}{1,2}] = "string1"

    println(m[struct{a, b int}{1,2}])

    var a struct{a, b int} = struct{a, b int}{5,6}
    var b TestStruct = TestStruct{3,4}

    m = TestMap{
        struct{a, b int}{1,2}: "string2",
        a: "string3",
        b: "string4",
    }

    println(m[struct{a, b int}{1,2}])
    println(m[a])
    println(m[b])
    println("len:", len(m))

    a.a = 1
    a.b = 2
    println(m[a])

    unknown, notSet := m[struct{a, b int}{99,99}]
    println("unknown: ", len(unknown))
    println("notSet: ", notSet)

    for k, v := range m {
        println("k: ", k.a, " ", k.b)
        println("v: ", v)
    }
}