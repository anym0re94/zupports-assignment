import { shallowMount, createLocalVue } from "@vue/test-utils"
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import notfound from '@/pages/404.vue'

const localVue = createLocalVue()
localVue.use(BootstrapVue)
localVue.use(IconsPlugin)

const factory = () => {
    return shallowMount(notfound, {
        localVue,
    })
}

describe('notfound', () => {
    test('mounts properly', () => {
        const wrapper = factory()
        expect(wrapper.vm).toBeTruthy()
    })
})
