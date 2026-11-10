# ProGuard rules for Lanka News Hub release builds

# Flutter wrapper
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.**  { *; }
-keep class io.flutter.util.**  { *; }
-keep class io.flutter.view.**  { *; }
-keep class io.flutter.**  { *; }
-keep class io.flutter.plugins.**  { *; }

# Google Play Core split install & deferred components (suppress warnings for unused split features)
-dontwarn com.google.android.play.core.**
-dontwarn com.google.android.play.core.splitcompat.**
-dontwarn com.google.android.play.core.splitinstall.**
-dontwarn com.google.android.play.core.tasks.**

# Models preservation for JSON serialization
-keep class com.lankanewshub.app.models.** { *; }

# Net / HTTP
-dontwarn java.lang.invoke.**
-dontwarn java.nio.file.**
-dontwarn javax.annotation.**
