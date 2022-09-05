package main

func main() {
	test_slice()
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
