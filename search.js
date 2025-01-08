document.addEventListener("DOMContentLoaded", () => {
    const searchbar = document.querySelector("#searchbar");
    const navigation = document.querySelector(".navigation");

    searchbar.addEventListener("focus", () => {
        navigation.classList.add("hidden");
    });

    searchbar.addEventListener("blur", () => {
        navigation.classList.remove("hidden");
    });
});
