package main

import "countsort"
import "quicksort"

func main() {
	var array1 = []int{12, 2, 35, 4, 59, 6, 7, -8, -99, 0, 10}

	// count sort
	println("Unsorted array:")
	printArray(array1)
	countsort.CountSort(array1)
	println("Sorted array:")
	printArray(array1)

	// quick sort
	var array2 = []int{35, 6, -3, 67, 0, 3, -12, 345, 42, 47, 124}
	println("Unsorted array:")
	printArray(array2)
	quicksort.QuickSort(array2)
	println("Sorted array:")
	printArray(array2)
}

func printArray(array []int) {
	for _, value := range array {
		print(value, " ")
	}

	println()
}
