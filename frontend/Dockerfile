FROM node:22-alpine

RUN apk add --no-cache git

WORKDIR /app

COPY package.json ./

RUN yarn install --no-lockfile --ignore-engines

COPY . .

EXPOSE 3000

ENV HOST=0.0.0.0
ENV PORT=3000

CMD ["yarn", "dev", "--", "--host", "0.0.0.0"]