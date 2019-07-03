<template>
    <div>
        <FullCalendar
                ref="fullCalendar"
                :plugins="calendarPlugins"
                :weekends="calendarWeekends"
                :events="calendarEvents"
                default-view="timeGridWeek"

                :header="{
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                }"
                @eventRender="handleEventRender"
                @eventClick="handleEventClick"
                @dateClick="handleDateClick"
        />
    </div>
</template>

<script>
    import FullCalendar from "@fullcalendar/vue";
    import dayGridPlugin from "@fullcalendar/daygrid";
    import timeGridPlugin from "@fullcalendar/timegrid";
    import interactionPlugin from "@fullcalendar/interaction";
    import Tooltip from 'tooltip.js';

    export default {
        props: ['resourceName', 'resourceId', 'field'],
        components: {
            FullCalendar
        },
        data() {
            return {
                calendarPlugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin,
                ],
                calendarWeekends: true,
                calendarEvents: [],
                service_id: this.resourceId,
                debugStr: []
            };
        },
        methods: {
            toggleWeekends() {
                this.calendarWeekends = !this.calendarWeekends; // update a property
            },
            gotoPast() {
                let calendarApi = this.$refs.fullCalendar.getApi();
                calendarApi.gotoDate("2000-01-01"); // call a method on the Calendar object
            },
            handleDateClick(arg) {
                // arg.dateStr
                if (this.resourceName === 'services'
                    || this.resourceName === 'schedules') {
                    window.open(`/nova/resources/seances/new?viaResource=${this.resourceName === 'schedules' ? 'services' : this.resourceName}&viaResourceId=${this.resourceId}&viaRelationship=seances`, "_blank");
                } else {
                    window.open(`/nova/resources/seances/new`, "_blank");
                }
            },
            handleEventClick(event) {
                if (event.url) {
                    window.open(event.url, "_blank");
                    return false;
                }
            },
            handleEventRender(info) {
                new Tooltip(info.el, {
                    html: true,
                    title: `<h4>${info.event.extendedProps.service}</h4>` +
                        `<p>${info.event.extendedProps.description}</p>` +
                        `<p>${info.event.extendedProps.price} руб.</p>` +
                        `<p>${info.event.extendedProps.duration} минут</p>` +
                        `<p>${info.event.extendedProps.user}</p>` +
                        `<p>${info.event.extendedProps.phone}</p>`,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
        },
        mounted() {
            var seances_promise;
            if (this.resourceName === 'clubs') {
                seances_promise = axios.get(`/v1/clubs/${this.resourceId}/seances`);
            } else if (this.resourceName === 'services'
                || this.resourceName === 'schedules') {
                seances_promise = axios.get(`/v1/services/${this.resourceId}/seances`);
            }
            seances_promise.then(response => {
                this.events = response.data.result;
                if (this.events) {
                    this.calendarEvents = [];
                    this.events.forEach((event) => {
                        let time = moment(event.time, ['hh:mm:ss']);
                        let start = moment(event.date).set({
                            hour: time.get('hour'),
                            minute: time.get('minute')
                        });
                        let end = moment(start).add({
                            minute: event.duration
                        });
                        let calendarEvent = {
                            title: event.description,
                            start: start.format(),
                            end: end.format(),
                            extendedProps: {
                                description: event.description,
                                price: event.price,
                                duration: event.duration,
                                service: event.service.name,
                                user: event.attendee.name,
                                phone: event.attendee.phone,
                            },
                            url: '/nova/resources/seances/' + event.id
                        };
                        this.calendarEvents.push(calendarEvent);
                    })
                }
            });
        },
    }
</script>


<style>
    @import "~@fullcalendar/core/main.css";
    @import "~@fullcalendar/daygrid/main.css";
    @import "~@fullcalendar/timegrid/main.css";

    .tooltip {
        z-index: 1;
        width: 250px;
        color: #252d37;
        background-color: #ffffff;
    }
</style>

