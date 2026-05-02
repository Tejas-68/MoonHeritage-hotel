# ── Stage 1: Build ────────────────────────────────────────────────────
FROM eclipse-temurin:21-jdk-alpine AS builder
WORKDIR /app

# Copy Maven wrapper + pom first (cache layer)
COPY pom.xml .
COPY mvnw .
COPY .mvn .mvn

# Download dependencies (cached unless pom changes)
RUN ./mvnw dependency:go-offline -B

# Copy source and build fat JAR
COPY src src
RUN ./mvnw package -DskipTests -B

# ── Stage 2: Run ──────────────────────────────────────────────────────
FROM eclipse-temurin:21-jre-alpine
WORKDIR /app

# Create a non-root user for security
RUN addgroup -S spring && adduser -S spring -G spring
USER spring:spring

# Copy only the JAR from the builder stage
COPY --from=builder /app/target/moonheritage-backend-1.0.0.jar app.jar

# Render / Railway set $PORT automatically
ENV PORT=8081
EXPOSE $PORT

ENTRYPOINT ["java", "-jar", "-Dserver.port=${PORT}", "app.jar"]
