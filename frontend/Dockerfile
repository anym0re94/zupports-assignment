FROM node:18.12.1-slim as build-stage

WORKDIR /app_frontend

COPY package*.json ./

RUN yarn install

COPY . /app_frontend

ARG NODE_OPTIONS=--openssl-legacy-provider
RUN yarn build


FROM nginx:1.23-alpine as deploy-stage

COPY --from=build-stage /app_frontend/dist /app_frontend

# COPY ./nginx.conf /etc/nginx/nginx.conf
