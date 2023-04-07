package quicksort

// Adapted code from https://github.com/TheAlgorithms/Go/blob/master/sort/quicksort.go

func partition(arr []int, low, high int) int {
	index := low - 1
	pivotElement := arr[high]

	for i := low; i < high; i++ {
		if arr[i] <= pivotElement {
			index += 1
			arr[index], arr[i] = arr[i], arr[index]
		}
	}
	arr[index+1], arr[high] = arr[high], arr[index+1]

	return index + 1
}

func quicksortRange(arr []int, low, high int) {
	if len(arr) <= 1 {
		return
	}

	if low < high {
		pivot := partition(arr, low, high)
		quicksortRange(arr, low, pivot-1)
		quicksortRange(arr, pivot+1, high)
	}
}

func QuickSort(arr []int) []int {
	quicksortRange(arr, 0, len(arr)-1)
	return arr
}
