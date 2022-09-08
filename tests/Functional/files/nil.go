package main

func main() {
	test_slice()
	test_pointer()
	test_map()
	test_func()
}

func test_slice() {
	println("test_slice")

	var x []int = nil
	println("cap:", cap(x), "len:", len(x))
	x = append(x, 1)
	println("cap:", cap(x), "len:", len(x))
	x = append(x, 1)
	println("cap:", cap(x), "len:", len(x))
	x = append(x, 1)
	println("cap:", cap(x), "len:", len(x))
	x = append(x, 1)

	for i, r := range x {
		println(i, r)
	}

	var y []uint
	println("cap:", cap(y), "len:", len(y))
	y = append(y, 1)
	println("cap:", cap(y), "len:", len(y))
	y = append(y, 1)
	println("cap:", cap(y), "len:", len(y))
	y = append(y, 1)
	println("cap:", cap(y), "len:", len(y))
	y = append(y, 1)

	for i, r := range y {
		println(i, r)
	}

	var z []uint = []uint{1, 2}
	z = nil
	println("cap:", cap(z), "len:", len(z))
	z = append(z, 1)
	println("cap:", cap(z), "len:", len(z))
	z = append(z, 1)
	println("cap:", cap(z), "len:", len(z))
	z = append(z, 1)
	println("cap:", cap(z), "len:", len(z))
	z = append(z, 1)

	for i, r := range z {
		println(i, r)
	}

	println(z == nil)
	println(z != nil)

	z = []uint{}
	println(z == nil)
	println(z != nil)

	var p1 *[]uint
	println(&z == p1)

	p1 = &z
	println(&z == p1)

	z = nil
	println(&z == p1)
}

func test_pointer() {
	println("test_pointer")

	var p1 *int
	var p2 *int = nil
	var p3 *int
	p3 = nil

	println(nil == p1)
	println(p1 == nil)
	println(p1 == p1)
	println(p1 == p2)
	println(p1 == p3)
	println(p3 == nil)

	var x int = 1
	p1 = &x
	println(nil == p1)
	println(p1 == nil)
	println(p1 == p1)
	println(p1 == p2)

	p2 = &x
	println(p1 == p2)

	p3 = p2
	println(p1 == p3)
	println(p1 == p2)
	println(p3 == nil)
}

func test_map() {
	println("test_map")

	var m1 map[int]int
	var m2 map[int]int = nil
	var m3 map[string]int = map[string]int{"a": 1}
	m3 = nil

	println(m1[1])
	println(m3["string"])

	println(len(m1))
	println(len(m2))
	println(len(m3))

	delete(m1, 1)
	delete(m2, 2)
	delete(m3, "a")
	delete(m3, "b")

	x, ok1 := m1[1]
	println(x, ok1)

	y, ok2 := m2[2]
	println(y, ok2)

	z, ok3 := m3["a"]
	println(z, ok3)

	println(m1 == nil)
	println(nil == m1)

	var p *map[int]int

	println(p == &m1)

	p = &m2

	println(&m2 == p)
	println(&m1 == p)

	m2 = make(map[int]int)
	println(m2 == nil)
	println(nil == m2)
	println(p == &m1)
}

func test_func() {
	println("test_func")

	var f1 func(int, ...[9]string) int
	var f2 func(bool) []bool = nil
	var f3 func(int) uint = func(x int) uint { return uint(x) }

	println(f3(1))
	println(f3 == nil)
	println(nil == f3)
	f3 = nil

	println(f1 == nil)
	println(nil == f2)
	println(f2 == nil)
	println(nil == f2)
	println(f3 == nil)
	println(nil == f3)
	println(f4 == nil)
	println(nil == f4)

	var p *func(int) uint

	println(p == &f3)

	p = &f3

	println(&f3 == p)
}

func f4(int) uint {
	return 0
}
