<template>
  <div>
    <b-navbar fixed="top" toggleable="sm" type="dark" variant="warning">
      <b-navbar-brand href="/">Zupports Assignment</b-navbar-brand>

      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav class="justify-content-center">
        <b-navbar-nav class="w-50">
          <b-form class="w-100">
            <b-form-input
              v-model="searchQuery"
              debounce="500"
              placeholder="Search for restaurant here !"
              size="md"
              class="font-weight-bold text-center"
            >
            </b-form-input>
          </b-form>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
    <b-container class="pt-3">
      <b-row>
        <b-col cols="12">
          <b-card class="pt-3">
            <b-card-title class="pl-3" v-html="cardTitle"></b-card-title>
            <b-overlay :show="isLoading" rounded="sm">
              <b-card-body class="mt-3">
                <b-row v-if="isError">
                  <b-col cols="12">
                    <span class="pl-2 text-danger">
                      {{ errorStatus }}
                    </span>
                  </b-col>
                </b-row>
                <b-row v-else>
                  <b-col
                    v-for="restaurant in restaurantList"
                    :key="restaurant.place_id"
                    cols="12"
                    md="6"
                    lg="4"
                    class="mb-3"
                  >
                    <RestaurantCard :restaurant="restaurant" />
                  </b-col>
                </b-row>
              </b-card-body>
            </b-overlay>
          </b-card>
        </b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
export default {
  name: 'IndexPage',
  data() {
    return {
      cancelSource: null,
      isCanceled: false,
      isLoading: false,
      isError: true,
      errorStatus: null,
      restaurantList: [],
      searchQuery: 'Bang sue',
    }
  },
  computed: {
    cardTitle() {
      if (this.isLoading) {
        return `Hang tight, we're searching for restaurants near <i>${this.searchQuery}</i> for you.`
      }

      if (!this.restaurantList.length) {
        return `Sorry, there is no restaurant near <i>${this.searchQuery}</i>.`
      }

      return `We found ${this.restaurantList.length} restaurant${
        this.restaurantList.length > 1 ? 's' : ''
      } near <i>${this.searchQuery}</i>.`
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
  mounted() {
    this.getRestaurant()
  },

  methods: {
    getRestaurant() {
      if (this.cancelSource) {
        this.cancelSource.cancel()
      }
      // console.info(this.abortController)

      this.isError = false
      this.errorStatus = null
      this.isLoading = true
      this.restaurantList = []

      this.cancelSource = this.$axios.CancelToken.source()
      // console.info('cancelSource', this.cancelSource)

      this.$axios
        .$get(`/restaurant`, {
          cancelToken: this.cancelSource.token,
          params: {
            search: this.searchQuery.trim(),
          },
        })
        .then(this.checkRestaurant)
        .then(this.renderRestaurant)
        .catch((error) => {
          if (this.$axios.isCancel(error)) {
            this.isCanceled = true
          } else {
            this.isCanceled = false
            this.isError = true
            this.errorStatus = error.message
          }

          this.restaurantList = []
        })
        .finally(() => {
          if (this.isCanceled) {
            this.isLoading = true
            return
          }

          this.isLoading = false
          this.cancelSource = null
        })
    },
    checkRestaurant(response) {
      this.isCanceled = false

      if (response.error) {
        this.isError = true
        this.errorStatus = response.detail
      }

      return response
    },
    renderRestaurant(restaurantList) {
      this.restaurantList = restaurantList
    },
  },
}
</script>
<style>
.container {
  margin-top: 56px;
}
</style>
