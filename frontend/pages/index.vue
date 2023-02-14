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
              debounce="650"
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
      cancelSource: null, // kind of global cancelSource (token) that will be used to cancel API request
      isCanceled: false, // determine if API is canceled or not
      isLoading: false, // determine if API is loading or not
      isError: true, // determine if API is error or not
      errorStatus: null, // error status to display
      restaurantList: [], // list of restaurant to be rendered
      searchQuery: 'Bang sue', // default keyword
    }
  },
  computed: {
    cardTitle() {
      // set the card title

      // if searchQuery is empty.
      if (!this.searchQuery.trim()) {
        return `Please type any search keyword on the box above.`
      }

      // if request currently loading.
      if (this.isLoading) {
        return `Hang tight, we're searching for restaurants near <i>${this.searchQuery}</i> for you.`
      }

      // if got no restaurant from API.
      if (!this.restaurantList.length) {
        return `Sorry, there is no restaurant near <i>${this.searchQuery}</i>.`
      }

      // if go some restaurants from API.
      return `We found ${this.restaurantList.length} restaurant${
        this.restaurantList.length > 1 ? 's' : ''
      } near <i>${this.searchQuery}</i>.`
    },
  },
  watch: {
    searchQuery(current, previous) {
      this.isError = false
      // track for searchQuery state changes. if it's empty value don't call API.
      if (current.trim() === '' || current === previous) {
        return
      }

      this.getRestaurant()
    },
  },
  mounted() {
    // call getRestaurant (API request) on mounted
    this.getRestaurant()
  },

  methods: {
    getRestaurant() {
      // if API request got interupted, cancel old request.
      if (this.cancelSource) {
        this.cancelSource.cancel()
      }

      // set state to loading.
      this.isError = false
      this.errorStatus = null
      this.isLoading = true
      this.restaurantList = []

      // create the cancel token to handling new keyworld interupt.
      this.cancelSource = this.$axios.CancelToken.source()

      // perform an API request to backend.
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
          // check if this request got cancelled or not.
          if (this.$axios.isCancel(error)) {
            this.isCanceled = true
          } else {
            this.isCanceled = false
            this.isError = true
            this.errorStatus = error.response?.data?.detail || error.message
          }

          this.restaurantList = []
        })
        .finally(() => {
          // if request got cancelled, which mean we have a new keyword request, loading state should remains true.
          if (this.isCanceled) {
            this.isLoading = true
            return
          }

          // clear loading state and dispose cancelSource.
          this.isLoading = false
          this.cancelSource = null
        })
    },
    checkRestaurant(response) {
      this.isCanceled = false

      // check if the response came with error key which mean something wrong from our API
      // if it does, pop an error.
      if (response.error) {
        this.isError = true
        this.errorStatus = response.detail
      }

      return response
    },
    renderRestaurant(restaurantList) {
      // set restaurantList state to API response data
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
