import { router } from "@inertiajs/vue3";

export function useNavigation() {
    const goToAllProducts = (options = {}) => {
        router.visit(route("products.index"));
        if (options.hideSuggestions) {
            options.hideSuggestions();
        }
    };

    return { goToAllProducts };
}
