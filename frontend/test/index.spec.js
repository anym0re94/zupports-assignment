import { shallowMount, createLocalVue } from "@vue/test-utils"
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

import index from '@/pages/index.vue'
import RestaurantCard from '@/components/RestaurantCard.vue'


const localVue = createLocalVue()
localVue.use(BootstrapVue)
localVue.use(IconsPlugin)

localVue.use(RestaurantCard)

const factory = () => {
    return shallowMount(index, {
        localVue,
    })
}

describe('index', () => {
    test('mounts properly', () => {
        const wrapper = factory()
        expect(wrapper.vm).toBeTruthy()
    })
})
