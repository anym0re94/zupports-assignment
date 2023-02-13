<template>
  <b-card no-body>
    <b-card-body>
      <b-card-title>{{ restaurant.name }}</b-card-title>
      <!-- <b-card-sub-title class="mb-2"> </b-card-sub-title> -->
      <b-card-text>
        <span v-b-tooltip.hover.right="restaurant.rating">
          <b-icon
            v-for="index in 5"
            :key="index"
            :icon="index <= restaurant.rating ? 'star-fill' : 'star'"
            variant="warning"
          >
          </b-icon>
        </span>
      </b-card-text>
    </b-card-body>

    <b-list-group flush>
      <b-list-group-item v-if="restaurant.opening_hours?.open_now">
        <b-icon icon="check-lg" class="mr-2" variant="success"></b-icon>
        Open Now
      </b-list-group-item>
      <b-list-group-item v-else>
        <b-icon icon="x-circle" class="mr-2" variant="danger"> </b-icon>
        Closed Now
      </b-list-group-item>
      <b-list-group-item>
        <b-icon icon="people-fill" class="mr-2"></b-icon>
        {{ restaurant.user_ratings_total }} user ratings
      </b-list-group-item>
      <b-list-group-item>
        <b-icon icon="map" class="mr-2" variant="danger"> </b-icon>
        <a :href="getGoogleMapLink(restaurant.place_id)" target="_blank">
          Open in Google Map
        </a>
      </b-list-group-item>
    </b-list-group>
    <b-card-footer>{{ restaurant.vicinity }}</b-card-footer>
  </b-card>
</template>

<script>
export default {
  props: {
    restaurant: {
      type: Object,
      required: true,
    },
  },
  methods: {
    getGoogleMapLink(place_id) {
      return `https://www.google.com/maps/search/?api=1&query=Google&query_place_id=${place_id}`
    },
  },
}
</script>
