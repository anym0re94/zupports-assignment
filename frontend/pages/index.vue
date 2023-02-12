<template>
  <div>
    <b-navbar fixed="top" toggleable="sm" type="dark" variant="warning">
      <b-navbar-brand href="/">Zupports Assignment</b-navbar-brand>

      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav class="mx-auto">
          <b-nav-form>
            <b-form-input
              v-model="searchQuery"
              debounce="500"
              placeholder="Search for restaurant here !"
              size="md"
              class=""
            >
            </b-form-input>
          </b-nav-form>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
    <b-container>
      <b-row>
        <b-col>
          <b-icon
            v-if="isLoading"
            icon="arrow-clockwise"
            animation="spin"
            font-scale=""
          ></b-icon>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          We found {{ restaurantList.length }} near {{ searchQuery }}
        </b-col>
      </b-row>
      <b-row>
        <b-col>{{ restaurantList }}</b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
export default {
  name: 'IndexPage',
  data() {
    return {
      searchQuery: 'Bang sue',
      isLoading: false,
      restaurantList: [],
    }
  },
  methods: {
    getRestaurant() {
      this.isLoading = true

      this.$axios
        .$get(`/restaurant`, {
          params: {
            search: this.searchQuery.trim(),
          },
        })
        .then(this.renderRestaurant)
        .catch(() => {
          this.restaurantList = []
        })
        .finally(() => {
          this.isLoading = false
        })
    },
    renderRestaurant(restaurantList) {
      this.restaurantList = restaurantList
    },
  },
  watch: {
    searchQuery(current, previous) {
      if (current === previous) {
        return
      }

      this.getRestaurant()
    },
  },
}
</script>
