const outer_menu_items = menu.querySelectorAll(".outer.menu_item");
const inner_menus = menu.querySelectorAll(".inner_menu");
const ind_triggers = document.querySelectorAll(".ind_trigger");
const ind_menus = document.querySelectorAll(".ind_menu");

const handleMenuClick = (e) => {
  const trigger = e.target;
  inner_menus.forEach((inner_menu) => {
    const same_oktmo =
      inner_menu.id.replace("menu_", "") == trigger.id.replace("trigger_", "");
    const is_hidden = inner_menu.classList.contains("hidden");

    if (same_oktmo && is_hidden) {
      inner_menu.classList.remove("hidden");
    } else {
      inner_menu.classList.add("hidden");
    }
  });
};

const handleIndClick = (e) => {
  const trigger = e.target;
  ind_menus.forEach((ind_menu) => {
    const correct_parent =
      trigger.id.replace("ind_trigger_", "") ==
      ind_menu.id.replace("ind_menu_", "");

    const is_hidden = ind_menu.classList.contains("hidden");

    if (correct_parent && is_hidden) {
      ind_menu.classList.remove("hidden");
    } else if (correct_parent && !is_hidden) {
      ind_menu.classList.add("hidden");
    }
  });
};

outer_menu_items.forEach((item) => {
  item.addEventListener("click", handleMenuClick);
});

ind_triggers.forEach((item) => {
  item.addEventListener("click", handleIndClick);
});
