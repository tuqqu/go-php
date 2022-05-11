package main

func main() {
	test_map_1()
	test_map_2()
}

func test_map_1() {
	m := make(map[string]int)

	m["k1"] = 7
	m["k2"] = 13
	println("map:", m)

	v1 := m["k1"]
	println("v1: ", v1)

	println("len:", len(m))

	delete(m, "k2")
	println("map:", m)

	value, ok := m["k2"]
	println("value:", value)
	println("ok:", ok)

	unknown, notSet := m["k99"]
	println("unknown:", unknown)
	println("notSet:", notSet)

    unknown, notSet = m["k99"]
	println("unknown:", unknown)
	println("notSet:", notSet)

	for k, v := range m {
		println("k,v: ", k, " ", v)
	}
}

func test_map_2() {
	n := map[string]uint{"foo": 1, "bar": 2}
	println("map:", n)

	setFooHundred(n)

	println("map:", n)

	println("map:", n)

	n["bar"] = 0
	var v1, v1ok = n["bar"]
	var v2, v2ok = n["baz"]

	println(v1, v1ok)
	println(v2, v2ok)
}

func setFooHundred(m map[string]uint) {
	m["foo"] = 100
}
