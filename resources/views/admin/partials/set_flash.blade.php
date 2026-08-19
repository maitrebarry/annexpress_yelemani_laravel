@if (session('notification'))
  @php $notification = session('notification'); @endphp
  <div class="flash-message d-flex align-items-center justify-content-between animate__animated animate__fadeInDown"
       style="width: 100%; padding: 16px 24px; border-radius: 16px;
              background: linear-gradient(135deg, {{ $notification['class'] }}, {{ $notification['class'] }}e6);
              backdrop-filter: blur(10px);
              color: #fff;
              box-shadow: 0 10px 25px -5px {{ $notification['class'] }}80;
              margin-bottom: 20px;
              border: 1px solid rgba(255,255,255,0.2);
              z-index: 1000; position: relative;">

    <div class="d-flex align-items-center gap-3">
      <div style="background: rgba(255,255,255,0.2); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <i class="{{ $notification['icon'] }}" style="font-size: 24px; color: #fff;"></i>
      </div>
      <span style="font-size: 15px; font-weight: 500; letter-spacing: 0.3px;">{{ $notification['message'] }}</span>
    </div>

    <button class="flash-close" onclick="this.parentElement.style.opacity='0'; setTimeout(() => this.parentElement.remove(), 300);"
            style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; cursor: pointer; transition: all 0.3s;"
            onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1.1)';"
            onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='scale(1)';">
      <i class="bx bx-x"></i>
    </button>
  </div>
@endif
