document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('food-search');
    const foodList = document.getElementById('food-list');

    if (!(searchInput && foodList)) return;

    const searchUrl = searchInput.dataset.searchUrl;
    let debounceTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const value = this.value;

        debounceTimer = setTimeout(() => {
            const search = encodeURIComponent(value);

            fetch(`${searchUrl}?search=${search}`)
                .then(res => res.json())
                .then(foods => {
                    if (foods.length === 0) {
                        foodList.innerHTML = '<p class="text-sm text-gray-400 py-3">Nenhum alimento encontrado.</p>';
                        return;
                    }

                    foodList.innerHTML = foods.map(food => `
                        <li class="flex items-center justify-between py-3 gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">${food.name}</p>
                                <p class="text-xs text-gray-500">${food.kcal} kcal por 100${food.unit}</p>
                            </div>
                            <form action="${foodList.dataset.addUrl}" method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="food_meal[food_id]" value="${food.id}">
                                <input type="number" name="food_meal[quantity]" step="0.01" min="0.01" placeholder="${food.unit}"
                                    class="w-20 bg-[#f6f6f6] border border-gray-200 rounded-md px-2 py-1 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <button type="submit" class="px-3 py-1 bg-primary text-white font-black text-xs rounded-md hover:opacity-90 cursor-pointer transition-colors">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </form>
                        </li>
                    `).join('');
                });
        }, 300);
    });
});