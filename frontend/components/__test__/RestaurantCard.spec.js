import { shallowMount, createLocalVue } from '@vue/test-utils'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import RestaurantCard from '../RestaurantCard'

const localVue = createLocalVue()
localVue.use(BootstrapVue)
localVue.use(IconsPlugin)


const factory = () => {
    return shallowMount(RestaurantCard, {
        localVue,
        propsData: {
            restaurant: {
                name: 'title'
            }
        }
    })
}

describe('RestaurantCard', () => {
    test('mounts properly', () => {
        const wrapper = factory()
        expect(wrapper.vm).toBeTruthy()
    })
})
