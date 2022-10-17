package main

func main() {
	test_1()
	test_2()
	test_3()
	test_4()
	test_5()
	test_6()
	test_7()
}

func test_1() {
	println("test_1")

	var z1 = complex(1, 4)
	z2 := complex(0, -2)

	var z3 = z1 + z2

	var z4 complex128 = z1

	println(z1, z2, z3, z4)
	println(real(z1), imag(z2))
}

func test_2() {
	println("test_2")

	var z1 complex128
	var z2 complex64

	println(z1, z2)
	println(real(z1), imag(z1))
	println(real(z2), imag(z2))
}

func test_3() {
	println("test_3")

	var z1 complex128 = complex(float64(78), -9.3)
	var z2 complex128 = complex(-23.23, +98.98)

	var z3 complex128 = z1 - z2

	var z4 complex64 = complex(43.1, float32(-1))
	var z5 complex64 = complex(-23.23, +98.98)

	var z6 complex64 = z4 / z5

	println(z1, z2, z3, z4, z5, z6)
}

func test_4() {
	println("test_4")

	const f1 float32 = 0.1
	const f2 = 32.1
	const f3 float64 = 66.6

	var z1 = complex(f1, f2)
	var z2 = complex(f2, f3)

	var z3 complex128 = complex(f3, f2)
	var z4 complex128 = complex(f3, f3)

	var z5 complex64 = complex(f1, f2)
	var z6 complex64 = complex(f1, f1)

	var f4 = real(z1)
	var f5 float32 = real(z5)
	var f6 float32 = imag(z5)
	var f7 float64 = real(z3)
	var f8 float64 = imag(z3)

	println(z1, z2, z3, z4, z5, z6)
	println(f4, f5, f6, f7, f8)
}

func test_5() {
    println("test_5")

    var z0 = -45.8 + 1i
    var z1 = 1 + 1i
    var z2 = -9i
    var z3 complex128 = 0.2i
    var z4 complex64 = -9-45i

    var z5 = z1 + z2 * z2 / z2 - z1

    var z6 complex64 = 1 * 9i

    println(z0, z1, z2, z3, z4, z5, z6)
    println(
        1+1+2i,
        3*3+1i,
        5/(4+1i),
        z0+1,
        z0*3.3,
        1i/4,
        1-8i/3,
        1-9i/4-4i,
    )
}

func test_6() {
    println("test_6")

    var z0 = -45.8 + 1i
    z0 += 1
    z0 += 4.4
    z0 *= 3

    println(z0)
}

func test_7() {
    println("test_7")

    var z = 1+8i
    var t = 4.4-1.4i

    println(z*t)
    println(z/t)
}
