package main

func main() {
	test_slice()
	test_pointer()
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
        println(i, r);
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
        println(i, r);
    }

    var z []uint = []uint{1,2}
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
        println(i, r);
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
